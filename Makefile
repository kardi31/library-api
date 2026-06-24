.PHONY: all
default: all;

analysis:
	vendor/bin/phpstan analyse -c phpstan.neon -l 4 \
	    src/

standards:
	vendor/bin/phpcs --colors --standard=cs-ruleset.xml src//

test:
	vendor/bin/phpunit

all: analysis
