<?php
/* @var $this \C\View\ConcreteContext */

// inline script on the foot position
$this->display('foot_inline_css', true);
$this->display('foot_inline_js', true);

// css import for template specifics, then page specifics
$this->display('template_footer_css', true);
$this->display('page_footer_css', true);
// js import for template specifics, then page specifics
$this->display('template_footer_js', true);
$this->display('page_footer_js', true);

// inline script on the last position
$this->display('last_inline_css', true);
$this->display('last_inline_js', true);
