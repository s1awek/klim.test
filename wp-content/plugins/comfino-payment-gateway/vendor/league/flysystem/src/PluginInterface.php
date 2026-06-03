<?php
namespace ComfinoExternal\League\Flysystem; interface PluginInterface{ public function getMethod(); public function setFilesystem(FilesystemInterface $filesystem);}