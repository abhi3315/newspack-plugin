/**
 * Dashboard Card
 */

/**
 * WordPress dependencies.
 */
import { Component } from '@wordpress/element';
import { chartBar, plugins, search, stretchWide, typography } from '@wordpress/icons';

/**
 * Internal dependencies.
 */
import { ButtonCard } from '../../../components/src';

class DashboardCard extends Component {
	/**
	 * Render.
	 */
	render() {
		const { name, description, slug, url } = this.props;
		const iconMap = {
			'site-design': typography,
			advertising: stretchWide,
			analytics: chartBar,
			seo: search,
		};
		return (
			<ButtonCard
				href={ url }
				title={ name }
				desc={ description }
				icon={ iconMap[ slug ] || plugins }
			/>
		);
	}
}
export default DashboardCard;
