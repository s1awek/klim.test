<?php
declare(strict_types=1); namespace Comfino\Common\Shop; interface OrderStatusAdapterInterface{ public function setStatus($orderId,$status):void;}