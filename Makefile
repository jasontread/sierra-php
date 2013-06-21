RPMDIR		 := rpm-build
SIERRA		 := sierra
SIERRA_DIR := $(RPMDIR)/var/www/sierra

all: setup repodata/repomd.xml teardown

setup:
	rm -f *.rpm
	rm -rf $(RPMDIR)
	mkdir -p $(RPMDIR)/SRPMS
	mkdir -p $(SIERRA_DIR)/app
	mkdir -p $(SIERRA_DIR)/etc/l10n
	rm -rf repodata
	mkdir -p repodata

repodata/repomd.xml: rpm
	createrepo .

teardown:
	rm -rf $(RPMDIR)
	rm -f sierra-php-debuginfo.rpm
	rm -rf repodata
	rm -f .rpmmacros

rpm: ${RPMDIR}/${SIERRA}.tgz .rpmmacros
	mkdir -p ${RPMDIR}/tmp
	HOME=$(CURDIR) rpmbuild --clean -ba --target noarch ./etc/sierra-php.spec
	mv $(RPMDIR)/SRPMS/*.rpm ./

${RPMDIR}/${SIERRA}.tgz:
	cp -r bin $(SIERRA_DIR)
	cp etc/*.* $(SIERRA_DIR)/etc
	rm -f $(SIERRA_DIR)/etc/sierra-config.xml
	rm -f $(SIERRA_DIR)/etc/sierra-php.spec
	rm -f $(SIERRA_DIR)/etc/time-zones.xml
	cp etc/l10n/*default* etc/l10n/*.dtd etc/l10n/installer.properties etc/l10n/README etc/l10n/sierra.properties etc/l10n/web-services-api.properties $(SIERRA_DIR)/etc/l10n
	cp -r lib $(SIERRA_DIR)
	cp -r www $(SIERRA_DIR)
	cd $(SIERRA_DIR) && find . -depth -name ".svn" -exec rm -rf '{}' \;
	cd ${RPMDIR} && tar -czf $(SIERRA).tgz var && rm -rf var

.rpmmacros: Makefile
	echo '%_topdir			$(CURDIR)/$(RPMDIR)' > $@
	echo '%_tmpdir			$(CURDIR)/$(RPMDIR)/tmp' >> $@
	echo '%_builddir		$(CURDIR)/$(RPMDIR)/tmp' >> $@
	echo '%_sourcedir	 $(CURDIR)/$(RPMDIR)' >> $@
	echo '%_specdir		 $(CURDIR)/spec' >> $@
	echo '%_rpmdir			$(CURDIR)' >> $@

clean:
	rm -rf *.rpm ./rpm-build/* .rpmmacros
