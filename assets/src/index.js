import { __, sprintf } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import TermOrderPanel from './components/TermOrderPanel';

const { registrations } = window.whazeTermOrderForPosts;

registrations.forEach( ( { taxonomy, label, restBase } ) => {
	registerPlugin( `whaze-term-order-for-posts-${ taxonomy }`, {
		render: () => (
			<PluginDocumentSettingPanel
				name={ `whaze-term-order-for-posts-${ taxonomy }` }
				title={ sprintf(
					/* translators: %s: taxonomy label, e.g. "Genres" */
					__( '%s (order)', 'whaze-term-order-for-posts' ),
					label
				) }
			>
				<TermOrderPanel
					taxonomy={ taxonomy }
					restBase={ restBase }
					label={ label }
				/>
			</PluginDocumentSettingPanel>
		),
	} );
} );
