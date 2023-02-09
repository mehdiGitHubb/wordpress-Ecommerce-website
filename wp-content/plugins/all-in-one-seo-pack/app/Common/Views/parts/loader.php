<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style>
.aioseo-loading-spinner {
	width: 35px;
	height: 35px;
	position: absolute;
}

.aioseo-loading-spinner .double-bounce1,
.aioseo-loading-spinner .double-bounce2 {
	width: 100%;
	height: 100%;
	border-radius: 50%;
	background-color: #fff;
	opacity: 0.6;
	position: absolute;
	top: 0;
	left: 0;

	-webkit-animation: aioseo-sk-bounce 1.3s infinite ease-in-out;
	animation: aioseo-sk-bounce 1.3s infinite ease-in-out;
}

.aioseo-loading-spinner.dark .double-bounce1,
.aioseo-loading-spinner.dark .double-bounce2 {
	background-color: #8C8F9A;
}

.aioseo-loading-spinner .double-bounce2 {
	-webkit-animation-delay: -0.65s;
	animation-delay: -0.65s;
}

.aioseo-loading-spinner {}
.aioseo-loading-spinner {}

@-webkit-keyframes aioseo-sk-bounce {
	0%, 100% { -webkit-transform: scale(0.0) }
	50% { -webkit-transform: scale(1.0) }
}

@keyframes aioseo-sk-bounce {
	0%, 100% {
		transform: scale(0.0);
		-webkit-transform: scale(0.0);
	} 50% {
		transform: scale(1.0);
		-webkit-transform: scale(1.0);
	}
}
</style>
<div style="height:50px; position:relative;">
	<div class="aioseo-loading-spinner dark" style="top:calc( 50% - 17px);left:calc( 50% - 17px);">
		<div class="double-bounce1"></div>
		<div class="double-bounce2"></div>
	</div>
</div>