#!/bin/bash
sed -i 's/# requirepass foobared/requirepass world/' /etc/redis/redis.conf
sed -i 's/# aclfile \/etc\/redis\/users.acl/aclfile \/etc\/redis\/users.acl/' /etc/redis/redis.conf
echo "user hello on >world +@all ~*" >> /etc/redis/users.acl