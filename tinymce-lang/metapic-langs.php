<?php

$strings = 'tinyMCE.addI18n({' . _WP_Editors::$mce_locale . ':{
    metapic:{
        linkTitle: "' . esc_js( __( 'Link content', 'metapic' ) ) . '",
        linkText: "' . esc_js( __( 'Metapic link', 'metapic' ) ) . '",
        imageTitle: "' . esc_js( __( 'Tag image', 'metapic' ) ) . '",
        imageText: "' . esc_js( __( 'Metapic image', 'metapic' ) ) . '",
        collageTitle: "' . esc_js( __( 'Add collage', 'metapic' ) ) . '",
        collageText: "' . esc_js( __( 'Metapic collage', 'metapic' ) ) . '"
    }
}})';