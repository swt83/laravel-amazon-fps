<?php

echo Form::open($endpoint);
foreach ($params as $key => $value)
{
    echo Form::hidden($key, $value);
}
echo Form::submit($submit);
echo Form::close();