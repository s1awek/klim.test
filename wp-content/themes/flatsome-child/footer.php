<?php

/**
 * The template for displaying the footer.
 *
 * @package          Flatsome\Templates
 * @flatsome-version 3.16.0
 */

global $flatsome_opt;
?>

</main>

<footer id="footer" class="footer-wrapper">

	<?php do_action('flatsome_footer'); ?>

</footer>

</div>
<script>
	(function(i) {
		var j = document.createElement("script");
		j.src = "https://cdn.allekurier.pl/mail-box/banner.js?hid=" + i;
		j.async = true;
		j.referrerPolicy = "no-referrer-when-downgrade";
		document.body.appendChild(j);
	})("869d5759-03fc-4e2f-8ae3-4547156f82e8");
</script>

<?php wp_footer(); ?>

</body>

</html>