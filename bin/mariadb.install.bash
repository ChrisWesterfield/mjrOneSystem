#!/usr/bin/env bash
export DEBIAN_FRONTEND=noninteractive
/usr/bin/sudo /usr/bin/debconf-set-selections <<< "mariadb-server-10.2 mysql-server/data-dir select ''"
/usr/bin/sudo /usr/bin/debconf-set-selections <<< "mariadb-server-10.2 mysql-server/root_password password 123"
/usr/bin/sudo /usr/bin/debconf-set-selections <<< "mariadb-server-10.2 mysql-server/root_password_again password 123"
/usr/bin/sudo /usr/bin/apt-get install -y mariadb-server