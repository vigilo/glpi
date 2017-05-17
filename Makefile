NAME = glpi

all:

include buildenv/Makefile.common.nopython

install: install_pkg

install_pkg: $(INFILES)
	-mkdir -p $(DESTDIR)$(DATADIR)/$(NAME)/plugins/vigilo
	cp -pr src/* $(DESTDIR)$(DATADIR)/$(NAME)/plugins/vigilo/

clean: clean_common

doc: sphinxdoc

.PHONY: all install install_pkg clean man doc
