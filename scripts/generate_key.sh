#!/bin/bash

gpg --batch --no-tty --gen-key <<EOF
    Key-Type: default
    Key-Length: 2048
    Subkey-Type: default
    Subkey-Length: 2048
    Name-Real: First Last
    Name-Email: your_mail@your.org
    Expire-Date: 0
    %no-protection
    %commit
EOF