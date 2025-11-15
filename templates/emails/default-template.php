<?php
/**
 * Default header template for email notifications.
 *
 * @since 1.0
 * @link  https://github.com/leemunroe/responsive-html-email-template
 */
if ( ! defined('ABSPATH') ) {
	exit;
}

$accent = Voxel\get_accent_color();
$logo_height = 50; // px
?>
<!DOCTYPE html>
<html <?php language_attributes() ?>>
<head>
	<meta name="viewport" content="width=device-width" />
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
	<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
	<style>
		/* GLOBAL RESETS */
		img { border: none; -ms-interpolation-mode: bicubic; max-width: 100%; }
		body {
			background-color: #f6f6f6;
			font-family: sans-serif;
			-webkit-font-smoothing: antialiased;
			font-size: 14px;
			line-height: 1.4;
			margin: 0;
			padding: 0;
			-ms-text-size-adjust: 100%;
			-webkit-text-size-adjust: 100%;
		}

		table { border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; }
		table td { font-family: sans-serif; font-size: 14px; vertical-align: top; }

		/* BODY & CONTAINER */
		.body { background-color: #f2f4f5; width: 100%; }

		/* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
		.container {
			display: block;
			margin: 0 auto !important;
			/* makes it centered */
			max-width: 580px;
			padding: 20px;
			width: 580px;
		}

		/* This should also be a block element, so that it will fill 100% of the .container */
		.content {
			box-sizing: border-box;
			display: block;
			margin: 0 auto;
			max-width: 580px;
		}

		/* HEADER, FOOTER, MAIN */
		.main { background: #ffffff; border-radius: 10px; width: 100%; box-shadow: 0 2px 20px #7e9ba924;}
		.wrapper { box-sizing: border-box; padding: 40px; }
		.content-block { padding-bottom: 10px; padding-top: 10px; }
		.footer { clear: both; margin-top: 10px; text-align: center; width: 100%; }
		.footer td, .footer p, .footer span, .footer a { color: #999999; font-size: 12px; text-align: center; }

		/* TYPOGRAPHY */
		h1, h2, h3, h4 {
			font-family: sans-serif;
			line-height: 1.3;
			margin: 0;
			margin-bottom: 15px;
			font-weight: 600;
			color: #484c5d;
		}
		h1 {
			font-size: 26px;
			text-align: left;
		}

		/* Primary link */
		.ts-email-content > a {
			background-color: <?= $accent ?>;
			border-radius: 10px;
			box-sizing: border-box;
			color: #fff;
			cursor: pointer;
			display: block;
			font-size: 15px;
			text-align: center;
			font-weight: bold;
			margin: 5px 5px 5px 0;
			padding: 9px;
			text-decoration: none;
			display: block;
		}
		p, ul, ol, .ts-email-content {
			font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Ubuntu,sans-serif;
			font-size: 16px;
			line-height: 22px;
			color: #525f7f;
			font-weight: normal;
			margin: 0;
			margin-bottom: 20px;
		}
		p li, ul li, ol li { list-style-position: inside; margin-left: 5px; }
		a { color: <?= $accent ?>; text-decoration: underline; }

		.ts-email-top {
			border-bottom: 1px solid #b3bdc2;
			margin-bottom: 50px;
			padding-bottom: 20px;
		}

		.ts-email-bottom {
			border-top: 1px solid #b3bdc2;
			margin-top: 50px;
			padding-top: 20px;
		}

		/* BUTTONS */
		.mbtn {
			background-color: transparent;
			border: 2px solid <?= $accent ?>;
			border-radius: 50px;
			box-sizing: border-box;
			color: <?= $accent ?>;
			cursor: pointer;
			display: inline-block;
			font-size: 14px;
			font-weight: bold;
			margin: 5px 5px 5px 0;
			padding: 8px 20px;
			text-decoration: none;
		}

		.mbtn.mbtn1 { background-color: <?= $accent ?>; border-color: <?= $accent ?>; color: #ffffff; }

		.btn { box-sizing: border-box; width: 100%; }
		.btn > tbody > tr > td { padding-bottom: 15px; }
		.btn table { width: auto; }
		.btn table td { background-color: #ffffff; border-radius: 5px; text-align: center; }
		.btn a {
			background-color: #ffffff;
			border: solid 1px <?= $accent ?>;
			border-radius: 5px;
			box-sizing: border-box;
			color: <?= $accent ?>;
			cursor: pointer;
			display: inline-block;
			font-size: 14px;
			font-weight: bold;
			margin: 0;
			padding: 12px 25px;
			text-decoration: none;
		}

		.btn-primary table td { background-color: <?= $accent ?>; }
		.btn-primary a { background-color: <?= $accent ?>; border-color: <?= $accent ?>; color: #ffffff; }

		/* OTHER STYLES THAT MIGHT BE USEFUL */
		.last { margin-bottom: 0; }
		.first { margin-top: 0; }
		.align-center { text-align: center; }
		.align-right { text-align: right; }
		.align-left { text-align: left; }
		.clear { clear: both; }
		.mt0 { margin-top: 0; }
		.mb0 { margin-bottom: 0; }

		.preheader {
			color: transparent;
			display: none;
			height: 0;
			max-height: 0;
			max-width: 0;
			opacity: 0;
			overflow: hidden;
			mso-hide: all;
			visibility: hidden;
			width: 0;
		}

		.powered-by a { text-decoration: none; }
		hr { border: 0; border-bottom: 1px solid #f6f6f6; margin: 20px 0; }

		/* RESPONSIVE AND MOBILE FRIENDLY STYLES */
		@media only screen and (max-width: 620px) {
			table[class=body] h1 { font-size: 28px !important; margin-bottom: 10px !important; }
			table[class=body] p, table[class=body] ul, table[class=body] ol, table[class=body] td,
			table[class=body] span, table[class=body] a { font-size: 16px !important; }
			table[class=body] .wrapper, table[class=body] .article { padding: 20px !important; }
			table[class=body] .content { padding: 0 !important; }
			table[class=body] .container { padding: 0 !important; width: 100% !important; }
			table[class=body] .main { border-left-width: 0 !important; border-radius: 0 !important; border-right-width: 0 !important; }
			table[class=body] .btn table { width: 100% !important; }
			table[class=body] .btn a { width: 100% !important; }
			table[class=body] .mbtn { width: 100% !important; margin-right: 0px !important; text-align: center !important; }
			table[class=body] .img-responsive { height: auto !important; max-width: 100% !important; width: auto !important; }
		}

		/* PRESERVE THESE STYLES IN THE HEAD */
		@media all {
			.ExternalClass { width: 100%; }
			.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font,
			.ExternalClass td, .ExternalClass div { line-height: 100%; }
			.apple-link a {
				color: inherit !important;
				font-family: inherit !important;
				font-size: inherit !important;
				font-weight: inherit !important;
				line-height: inherit !important;
				text-decoration: none !important;
			}
			.btn-primary table td:hover { background-color: #34495e !important; }
			.btn-primary a:hover { background-color: #34495e !important; border-color: #34495e !important; }
		}
	</style>
</head>
<body class="">
	<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
		<tr>
			<td>&nbsp;</td>
			<td class="container">
				<div class="content">
					<table role="presentation" class="main">
						<tr>
							<td class="wrapper">
								<table role="presentation" border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td class="ts-email-content">
											<?php if ( $logo = wp_get_attachment_image_src( get_option('site_logo'), 'medium' ) ): ?>
												<div class="ts-email-top">
													<img src="<?= esc_attr( $logo[0] ) ?>" height="<?= intval( $logo_height ) ?>" width="<?= intval( ( $logo_height * $logo[1] ) / $logo[2] ) ?>">
												</div>
											<?php endif ?>
											<?= $message ?>
											<div class="ts-email-bottom">
												<p><?= \Voxel\get_email_footer_text() ?></p>
											</div>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>
			</td>
			<td>&nbsp;</td>
		</tr>
	</table>
</body>
</html>
