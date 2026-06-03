<?php
namespace ComfinoExternal\Psr\Http\Message; interface RequestFactoryInterface{ public function createRequest(string $method,$uri):RequestInterface;}