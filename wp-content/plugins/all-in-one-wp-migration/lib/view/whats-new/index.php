<?php
/**
 * Copyright (C) 2014-2020 ServMask Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ███████╗███████╗██████╗ ██╗   ██╗███╗   ███╗ █████╗ ███████╗██╗  ██╗
 * ██╔════╝██╔════╝██╔══██╗██║   ██║████╗ ████║██╔══██╗██╔════╝██║ ██╔╝
 * ███████╗█████╗  ██████╔╝██║   ██║██╔████╔██║███████║███████╗█████╔╝
 * ╚════██║██╔══╝  ██╔══██╗╚██╗ ██╔╝██║╚██╔╝██║██╔══██║╚════██║██╔═██╗
 * ███████║███████╗██║  ██║ ╚████╔╝ ██║ ╚═╝ ██║██║  ██║███████║██║  ██╗
 * ╚══════╝╚══════╝╚═╝  ╚═╝  ╚═══╝  ╚═╝     ╚═╝╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Kangaroos cannot jump here' );
}
?>

<div class="ai1wm-whats-new-container">
	<div class="ai1wm-whats-new-hero-container">
		<img src="<?php echo wp_make_link_relative( AI1WM_URL ); ?>/lib/view/assets/img/whats-new/hero.png?v=<?php echo AI1WM_VERSION; ?>" />
		<div class="ai1wm-whats-new-hero-text">
			<div class="ai1wm-whats-new-hero-date">01/17/2023</div>
			<div class="ai1wm-whats-new-hero-title"><span style="color: #A06AB4">Premium:</span> Exclude database tables from exported files</div>
			<div class="ai1wm-whats-new-hero-content">
				With this new feature, you now have even more control over your migration process. You can choose to leave out any unnecessary or sensitive data, resulting in smaller, more manageable export files. This can be especially useful for users who have a large amount of data, and want to avoid hitting server limits during the migration process.<br /><br />
				Using the feature is easy: simply select the tables you want to exclude from the export page and the plugin will take care of the rest.<br /><br />
				Whether you're migrating a website to a new host, transferring data between sites, or creating a backup, All-in-One WP Migration's new table exclusion feature has got you covered. Try it out today and see the difference it makes!
			</div>
		</div>
	</div>
</div>
