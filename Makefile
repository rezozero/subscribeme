#
# Base theme
# Development tasks
#

test:
	vendor/bin/phpcbf -p
	vendor/bin/phpstan analyse -c phpstan.neon -l max src