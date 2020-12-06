<?php
include ('WidgetCalculator.php');
if($_GET['widgetCount']) {
    $calculator = new WidgetCalculator($_GET['widgetCount']);
    $orderSet = $calculator->getOrderSet();
    $toReturn = 'You are given the packs: <br>';
    foreach($orderSet as $order) {
        $toReturn .= $order . ', ';
    }
    echo '<span>' . $toReturn . '</span>';
}
