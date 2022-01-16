#!/usr/bin/make -f

SRCFILES := $(wildcard ccm19-integration/*.php ccm19-integration/*.txt)

all: ccm19-integration/languages/ccm19-integration-de_DE.mo

ccm19-integration/languages/ccm19-integration.pot: ${SRCFILES}
	php wp-cli.phar i18n make-pot ccm19-integration/ ccm19-integration/languages/ccm19-integration.pot

ccm19-integration/languages/ccm19-integration-%.po: ccm19-integration/languages/ccm19-integration.pot
	if [ -e "$@" ]; then \
		msgmerge "$@" "$<" || true; \
	else \
		msginit -i "$<" -o "$@" -l "$*"; \
	fi

%.mo: %.po
	msgfmt -o "$@" "$<"

.PHONY: all

.SECONDARY:
