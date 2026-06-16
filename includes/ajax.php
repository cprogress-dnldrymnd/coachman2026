<?php
add_action('wp_ajax_nopriv_dealer_details_ajax', 'dealer_details_ajax'); // for not logged in users
add_action('wp_ajax_dealer_details_ajax', 'dealer_details_ajax');
function dealer_details_ajax()
{
	$post_id = $_POST['post_id'];
	$_post = get_post($post_id);
	$stocks = carbon_get_post_meta($post_id, 'stocks');
	$wpsl_phone = get_post_meta($post_id, 'wpsl_phone', true);
	$wpsl_email = get_post_meta($post_id, 'wpsl_email', true);
	$wpsl_url = get_post_meta($post_id, 'wpsl_url', true);

?>
	<div class="dealer--details">
		<h3 class="mb-4 fw-semibold"><?= $_post->post_title ?></h3>
		<div class="dealer--desc">
			<?= $_post->post_content ?>
		</div>

		<ul class="meta--details mt-4">
			<?php if ($wpsl_phone) { ?>
				<li>
					<span class="label">Phone:</span>
					<span class="value"><a href="tel:<?= $wpsl_phone ?>"><?= $wpsl_phone ?></a></span>
				</li>
			<?php } ?>
			<?php if ($wpsl_email) { ?>
				<li>
					<span class="label">Email:</span>
					<span class="value"><a href="mailto:<?= $wpsl_email ?>"><?= $wpsl_email ?></a></span>
				</li>
			<?php } ?>
			<?php if ($wpsl_url) { ?>
				<li>
					<span class="label">Website:</span>
					<span class="value"><a href="<?= fixUrl($wpsl_url) ?>" target="_blank"><?= $wpsl_url ?></a></span>
				</li>
			<?php } ?>
		</ul>
		<?php if ($stocks) { ?>
			<?php
			$years = [];
			foreach ($stocks as $stock) {
				foreach ($stock['years'] as $year) {
					if (!in_array($year['year'], $years)) {
						$years[] = $year['year'];
					}
				}
			}
			sort($years);

			?>

			<div class="listings--posts mt-4">
				<h4 class="fw-semibold mb-3">In Stock</h4>
				<table class="table">
					<?php
					echo '<tr>';
					echo '<th>Model</th>';
					foreach ($years as $year) {
						echo '<th class="text-center">' . $year . '</th>';
					}
					echo '</tr>';

					$stock_years = [];
					foreach ($stocks as $stock) {

						foreach ($stock['years'] as $year) {
							$stock_years[] = $year['year'];
						}
						echo '<tr>';
						echo '<td>' . $stock['listing_name'] . '</td> ';
						foreach ($years as $year) {
							if (in_array($year, $stock_years)) {
								echo '<td class="tick-icon text-center"></td>';
							} else {
								echo '<td></td>';
							}
						}
						echo '</tr>';
						$stock_years = [];
					}
					?>
				</table>
			</div>
		<?php } ?>

	</div>
<?php
	die();
}


/**
 * Fixes a URL by prepending 'https://' if the protocol is missing.
 *
 * This function uses parse_url() to check for an existing scheme (http, https, etc.).
 * If no scheme is found, it adds the secure 'https://' protocol to the beginning of the URL.
 *
 * @param string $url The URL string to fix.
 * @return string The fixed URL.
 */
function fixUrl($url)
{
	// Return an empty string if the input is empty to avoid errors
	if (empty($url)) {
		return '';
	}

	// Parse the URL to check for a scheme (e.g., http, https)
	$parsed = parse_url($url);

	// If no scheme is present, prepend 'https://'
	if (empty($parsed['scheme'])) {
		$url = 'https://' . $url;
	}

	// Return the fixed URL
	return $url;
}
