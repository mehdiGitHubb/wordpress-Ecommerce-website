<?php
/**
 * XSL formatPriority template for the sitemap.
 *
 * @since 4.1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable
?>
<xsl:template name="formatPriority">
	<xsl:param name="priority"/>

	<xsl:variable name="priorityLevel">
		<xsl:choose>
			<xsl:when test="$priority &lt;= 0.5">low</xsl:when>
			<xsl:when test="$priority &gt;= 0.6 and $priority &lt;= 0.8">medium</xsl:when>
			<xsl:when test="$priority &gt;= 0.9">high</xsl:when>
		</xsl:choose>
	</xsl:variable>

	<xsl:variable name="priorityLabel">
		<xsl:choose>
			<xsl:when test="$priorityLevel = 'low'"><?php _e( 'Low', 'all-in-one-seo-pack' ); ?></xsl:when>
			<xsl:when test="$priorityLevel = 'medium'"><?php _e( 'Medium', 'all-in-one-seo-pack' ); ?></xsl:when>
			<xsl:when test="$priorityLevel = 'high'"><?php _e( 'High', 'all-in-one-seo-pack' ); ?></xsl:when>
		</xsl:choose>
	</xsl:variable>

	<div>
		<xsl:attribute name="class">
			<xsl:value-of select="concat('priority priority--', $priorityLevel)" />
		</xsl:attribute>
		<xsl:value-of select="$priorityLabel" />
	</div>
</xsl:template>
