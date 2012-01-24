<?php
 
class CustomSiteConfig extends DataObjectDecorator {
     
    function extraStatics() {
        return array(
            'db' => array(
                'refresh_token' => 'Varchar(70)',
                'access_token' => 'Varchar(70)',
                'expiry' => 'Int',
                'token_type' => 'Varchar'
            )
        );
    }
 
}
