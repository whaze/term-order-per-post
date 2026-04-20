import { __, sprintf } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import SortableTermList from './SortableTermList';

/**
 * Renders the ordering panel content for a single taxonomy.
 *
 * Rendered inside a PluginDocumentSettingPanel — no extra PanelBody wrapper needed.
 *
 * @param {Object} props
 * @param {string} props.taxonomy The taxonomy slug (e.g. 'category', 'genre').
 * @param {string} props.restBase The taxonomy REST base (e.g. 'categories', 'genre').
 * @param {string} props.label    Human-readable taxonomy label.
 */
export default function TermOrderPanel( { taxonomy, restBase, label } ) {
	const { metaKey } = window.termOrderPerPost;

	const { assignedTermIds, storedOrder, fullMetaOrder } = useSelect(
		( select ) => {
			const store = select( editorStore );

			// Term IDs are stored under the REST base key on the post object.
			const terms = store.getEditedPostAttribute( restBase ) ?? [];

			const meta = store.getEditedPostAttribute( 'meta' ) ?? {};
			const raw = meta?.[ metaKey ] ?? '';

			let order = [];
			let fullMeta = {};
			try {
				if ( raw ) {
					const parsed = JSON.parse( raw );
					order = parsed[ taxonomy ] ?? [];
					fullMeta = parsed;
				}
			} catch {
				// Silently ignore malformed meta.
			}

			return {
				assignedTermIds: terms,
				storedOrder: order,
				fullMetaOrder: fullMeta,
			};
		},
		[ taxonomy, restBase, metaKey ]
	);

	const { editPost } = useDispatch( editorStore );

	if ( ! assignedTermIds || assignedTermIds.length === 0 ) {
		return (
			<p>
				{ sprintf(
					/* translators: %s: taxonomy name */
					__(
						'Assign terms first using the %s panel to order them here.',
						'term-order-per-post'
					),
					label
				) }
			</p>
		);
	}

	/**
	 * Merge the new order for this taxonomy into the full meta object and persist.
	 *
	 * @param {number[]} newOrder Ordered array of term IDs.
	 */
	function handleOrderChange( newOrder ) {
		const updated = { ...fullMetaOrder, [ taxonomy ]: newOrder };
		editPost( { meta: { [ metaKey ]: JSON.stringify( updated ) } } );
	}

	return (
		<SortableTermList
			taxonomy={ taxonomy }
			assignedTermIds={ assignedTermIds }
			order={ storedOrder }
			onOrderChange={ handleOrderChange }
		/>
	);
}
