import { __, sprintf } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import TermOrderPanel from './components/TermOrderPanel';

const { registrations } = window.termOrderPerPost;

registrations.forEach( ( { taxonomy, label, restBase } ) => {
	registerPlugin( `term-order-per-post-${ taxonomy }`, {
		render: () => (
			<PluginDocumentSettingPanel
				name={ `term-order-per-post-${ taxonomy }` }
				title={ sprintf(
					/* translators: %s: taxonomy label, e.g. "Genres" */
					__( '%s (order)', 'term-order-per-post' ),
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
