#!/usr/bin/env bash
export DEBIAN_FRONTEND=noninteractive
/usr/bin/sudo /usr/bin/debconf-set-selections <<< "mysql-community-server mysql-community-server/root-pass password 123"
/usr/bin/sudo /usr/bin/debconf-set-selections <<< "mysql-community-server mysql-community-server/re-root-pass password 123"
/usr/bin/sudo DEBIAN_FRONTEND=noninteractive  /usr/bin/apt-get install -y mysql-community-client mysql-community-server mysql-community-source mysql-utilities