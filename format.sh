#!/bin/bash

find resources/views/ -type d | while read -r dir; do
    echo
    echo "Formatting: $dir"
    blade-formatter --progress --write "$dir"/*.blade.php
done
