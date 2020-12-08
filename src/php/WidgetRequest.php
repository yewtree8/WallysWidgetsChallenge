<?php
include ('WidgetCalculator.php');
if($_GET['widgetCount']) {
    $calculator = new WidgetCalculator($_GET['widgetCount']);
    $orderSet = $calculator->getOrderSet();
    $toReturn = 'You will receive the these packs for your order: <br>';
    foreach($orderSet as $order) {
        $toReturn .= $order . ', ';
    }
    //echo '<span>' . $toReturn . '</span>';
}
