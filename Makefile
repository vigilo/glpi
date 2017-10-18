NAME = glpi
php := $(shell which php)

all:

include buildenv/Makefile.common.nopython

install: install_base install_data install_permissions

install_pkg: install_base install_data

install_base: $(INFILES)
	mkdir -p $(DESTDIR)$(DATADIR)/$(NAME)/plugins/
	mkdir -p $(DESTDIR)$(SYSCONFDIR)/vigilo/vigiconf/conf.d/groups/managed
	mkdir -p $(DESTDIR)$(SYSCONFDIR)/vigilo/vigiconf/conf.d/hosts/managed
	mkdir -p $(DESTDIR)$(SYSCONFDIR)/vigilo/vigiconf/conf.d/hlservices/managed
	cp -pr src/plugins/vigilo $(DESTDIR)$(DATADIR)/$(NAME)/plugins/

install_data: pkg/init pkg/sudoers
	install -p -m 755 -D pkg/init $(DESTDIR)$(INITDIR)/$(PKGNAME)
	install -p -m 644 -D pkg/sudoers $(DESTDIR)$(SYSCONFDIR)/sudoers.d/$(PKGNAME)

install_permissions:
	-/usr/sbin/usermod -a -G apache vigiconf
	chown root:root $(DESTDIR)$(INITDIR)/$(PKGNAME)
	chown root:root $(DESTDIR)$(SYSCONFDIR)/sudoers.d/$(PKGNAME)
	chown vigiconf:apache $(DESTDIR)$(SYSCONFDIR)/vigilo/vigiconf/conf.d/groups/managed
	chown vigiconf:apache $(DESTDIR)$(SYSCONFDIR)/vigilo/vigiconf/conf.d/hosts/managed
	chown vigiconf:apache $(DESTDIR)$(SYSCONFDIR)/vigilo/vigiconf/conf.d/hlservices/managed
	chmod 0770 $(DESTDIR)$(SYSCONFDIR)/vigilo/vigiconf/conf.d/groups/managed
	chown 0770 $(DESTDIR)$(SYSCONFDIR)/vigilo/vigiconf/conf.d/hosts/managed
	chown 0770 $(DESTDIR)$(SYSCONFDIR)/vigilo/vigiconf/conf.d/hlservices/managed

clean: clean_common

doc: sphinxdoc

serve:
	$(php) -S 0.0.0.0:8080 -t src

.PHONY: all install install_pkg clean man doc serve
