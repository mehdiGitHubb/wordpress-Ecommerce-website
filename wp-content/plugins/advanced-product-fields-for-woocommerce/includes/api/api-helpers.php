<?php

function wapf_has_setting( $name = '' ) {

    return wapf()->has_setting( $name );
}

function wapf_get_setting( $name, $value = null ) {

    if( wapf_has_setting($name) ) {
        $value =  wapf()->get_setting( $name );
    }

    $value = apply_filters( "wapf/setting/{$name}", $value );

    return $value;
}
