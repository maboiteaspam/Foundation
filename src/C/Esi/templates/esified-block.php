<?php
/**
 * This block is responsible to display
 * an ESI syntax url
 * to let front proxy
 * resolve it JIT.
 */

/* @var $this \C\View\ConcreteContext */
/* @var $url string */
/* @var $target string */
?>
<esi:include src="<?php echo $url; ?>?target=<?php echo $target; ?>"></esi:include>