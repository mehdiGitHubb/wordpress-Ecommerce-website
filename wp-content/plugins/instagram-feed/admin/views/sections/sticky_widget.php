<div class="sbi-stck-wdg">
	<?php
		$icons = InstagramFeed\Builder\SBI_Feed_Builder::builder_svg_icons();
		$smashballoon_info = InstagramFeed\Builder\SBI_Feed_Builder::get_smashballoon_info();
	?>
	<div class="sbi-stck-pop" v-if="stickyWidget">
		<div class="sbi-stck-el sbi-stck-el-upgrd sb-btn-orange">
			<div class="sbi-stck-el-icon"><?php echo $icons[$smashballoon_info['upgrade']['icon']] ?></div>
			<div class="sbi-stck-el-txt sb-small-p sb-bold" style="color: #fff;"><?php echo __('Get All Access Bundle','instagram-feed') ?></div>
            <div class="sbi-chevron">
                <svg width="7" height="10" viewBox="0 0 7 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1.3332 0L0.158203 1.175L3.97487 5L0.158203 8.825L1.3332 10L6.3332 5L1.3332 0Z" fill="white"/>
                </svg>
            </div>
            <a :href="links.popup.allAccessBundle" target="_blank" class="sbi-fs-a"></a>
		</div>

		<div class="sbi-stck-title"><?php echo __('Our Feeds for other platforms','instagram-feed') ?></div>

		<div class="sbi-stck-el-list sbi-fb-fs">
			<?php foreach ($smashballoon_info['platforms'] as $platform): ?>
				<div class="sbi-stck-el sbi-fb-fs">

					<div class="sbi-stck-el-icon" style="color:<?php echo $smashballoon_info['colorSchemes'][$platform['icon']] ?>;"><?php echo $icons[$platform['icon']] ?></div>
					<div class="sbi-stck-el-txt sb-small-text sb-small-p sb-dark-text"><?php echo $platform['name'] ?></div>
                    <div class="sbi-chevron">
                        <svg width="7" height="10" viewBox="0 0 7 10" fill="#8C8F9A" xmlns="http://www.w3.org/2000/svg"><path d="M1.3332 0L0.158203 1.175L3.97487 5L0.158203 8.825L1.3332 10L6.3332 5L1.3332 0Z" fill="#8C8F9A"></path></svg>
                    </div>
					<a href="<?php echo esc_url($platform['link'] ) ?>" target="_blank" class="sbi-fs-a"></a>
				</div>
			<?php endforeach ?>
		</div>
		<div class="sbi-stck-follow sbi-fb-fs">
			<span><?php echo __('Follow Us','instagram-feed') ?></span>
			<div class="sbi-stck-flw-links">
				<?php foreach ($smashballoon_info['socialProfiles'] as $social_key => $social): ?>
					<a href="<?php echo esc_url($social); ?>" target="_blank"  style="color:<?php echo $smashballoon_info['colorSchemes'][$social_key] ?>;"><?php echo $icons[$social_key] ?></a>
				<?php endforeach ?>
			</div>
		</div>
	</div>
	<div class="sbi-stck-wdg-btn" @click.prevent.default="toggleStickyWidget">
		<span v-if="!stickyWidget"><?php echo $icons['smash']; ?></span>
		<div v-else class="sbi-stck-wdg-btn-cls">
			<svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M14.501 1.77279L13.091 0.362793L7.50098 5.95279L1.91098 0.362793L0.500977 1.77279L6.09098 7.36279L0.500977 12.9528L1.91098 14.3628L7.50098 8.77279L13.091 14.3628L14.501 12.9528L8.91098 7.36279L14.501 1.77279Z" fill="#141B38"/>
			</svg>
        </div>
	</div>
</div>
