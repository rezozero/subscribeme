#
# Base theme
# Development tasks
#

test:
	vendor/bin/phpcbf -p
	vendor/bin/phpstan analyse -c phpstan.neon

changelog:
	git-cliff -o CHANGELOG.md

bump:
	git-cliff --bump -o CHANGELOG.md
