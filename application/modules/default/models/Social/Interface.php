<?php

interface Social_Interface {

    public function __construct($blog, $ip);
    public function getView();
    public function getBaseUrl();
    public function redirectUrl();

}