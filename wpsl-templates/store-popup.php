<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wpsl-popup">

    <h3><?php echo esc_html( $store['store'] ); ?></h3>

    <p>
        <?php echo esc_html( $store['address'] ); ?><br>
        <?php echo esc_html( $store['city'] ); ?><br>
        <?php echo esc_html( $store['state'] ); ?> <?php echo esc_html( $store['zip'] ); ?>
    </p>

    <?php if ( ! empty( $store['phone'] ) ) : ?>
        <p>Phone: <?php echo esc_html( $store['phone'] ); ?></p>
    <?php endif; ?>

    <?php if ( ! empty( $store['email'] ) ) : ?>
        <p>Email: <?php echo esc_html( $store['email'] ); ?></p>
    <?php endif; ?>

    <?php if ( ! empty( $store['url'] ) ) :

        $website = trim( $store['url'] );

        // Fix URLs saved without scheme
        if ( ! preg_match( '#^https?://#i', $website ) ) {
            $website = 'https://' . ltrim( $website, '/' );
        }

        $label = preg_replace( '#^https?://#i', '', $website );
        $label = rtrim( $label, '/' );
    ?>
        <p class="wpsl-store-website">
            Website:
            <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener">
                <?php echo esc_html( $label ); ?>
            </a>
        </p>
    <?php endif; ?>

    <p>
        <a href="#" class="wpsl-directions">
            <?php echo esc_html( $labels['directions'] ); ?>
        </a>
    </p>

</div>
