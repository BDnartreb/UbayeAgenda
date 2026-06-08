<?php

echo is_writable(__DIR__.'/../var/cache') ? 'WRITABLE' : 'NOT WRITABLE';