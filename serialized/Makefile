SPECIAL_TARGETS=first-letters-root.ser
ALL_TARGETS=$(SPECIAL_TARGETS)
DIST_TARGETS=$(SPECIAL_TARGETS)
UNICODE_VERSION=6.0.0

.PHONY: all dist clean

all: $(ALL_TARGETS)
	@echo 'Warning: messages are no longer serialized by this makefile.'

dist: $(DIST_TARGETS)

clean:
	rm -f $(ALL_TARGETS)

first-letters-root.ser: allkeys.txt ucd.all.grouped.xml
	php ../maintenance/language/generateCollationData.php

allkeys.txt:
	wget https://www.unicode.org/Public/UCA/$(UNICODE_VERSION)/allkeys.txt

ucd.all.grouped.xml: ucd.all.grouped.zip
	unzip ucd.all.grouped.zip ucd.all.grouped.xml

ucd.all.grouped.zip:
	wget https://www.unicode.org/Public/$(UNICODE_VERSION)/ucdxml/ucd.all.grouped.zip
