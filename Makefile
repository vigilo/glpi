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

serve:
	$(php) -S 0.0.0.0:8080 -t src

# Internationalisation
i18n: extract_messages identity_catalog update_catalog compile_catalog

extract_messages:
	find src/plugins/vigilo/ -name '*.php' | \
		xargs xgettext --keyword=_n:1,2,4t --keyword=__s:1,2t --keyword=__:1,2t \
		               --keyword=_e:1,2t --keyword=_x:1c,2,3t --keyword=_ex:1c,2,3t \
		               --keyword=_nx:1c,2,3,5t --keyword=_sx:1c,2,3t \
		                -L PHP --from-code=utf-8 \
		                -o src/plugins/vigilo/locales/glpi.pot\
		               --force-po --escape --strict --sort-output \
		               --add-comments=TRANSLATOR --copyright-holder=CSSI \
		               --package-name="Vigilo NMS" --foreign-user \
		               --msgid-bugs-address=contact.vigilo@c-s.fr

init_catalog: extract_messages
	msginit --no-translator -i src/plugins/vigilo/locales/glpi.pot --locale="$(LANG).UTF-8" -o "src/plugins/vigilo/locales/$(LANG).po"

update_catalog: extract_messages
	for po in `find src/plugins/vigilo/locales/ -name '*.po'`; do \
		touch $$po; \
		msgmerge -N -i -s -o "$$po" "$$po" src/plugins/vigilo/locales/glpi.pot; \
	done

identity_catalog: extract_messages
	$(MAKE) init_catalog LANG=en_GB

compile_catalog: update_catalog
	for lang in `find src/plugins/vigilo/locales/ -name '*.po'`; do \
		lang=`basename "$$lang" .po`; \
		echo -n "$$lang : " && msgfmt --statistics -o "src/plugins/vigilo/locales/$$lang.mo" "src/plugins/vigilo/locales/$$lang.po"; \
	done

tests:
	composer update
	vendor/bin/phpcs

doc apidoc:

.PHONY: all install install_pkg install_base install_data install_permissions \
	clean man doc serve tests doc apidoc \
	i18n extract_messages update_catalog identity_catalog compile_catalog
