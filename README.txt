Vigilo GLPI
===========

Ce composant sert de passerelle entre GLPI et Vigilo.

Dépendances
-----------
Le plugin vigilo-glpi nécessite de disposer de PHP >= 5.4.0 sur le système.
La version de PHP peut être vérifiée en lançant la commande suivante
depuis un terminal::

    php -v

De plus, les extensions PHP suivantes doivent être actives:

*   DOM
*   PCRE

La commande suivante peut-être utilisée pour vérifier que les extensions
sont correctement installées et activées (les extensions doivent apparaître
dans la sortie de la commande) ::

    php -m

Le plugin nécessite que VigiConf >= 4.0 soit installé sur la machine.
La version de VigiConf installée peut être vérifiée à l'aide de la commande
suivante::

    rpm -q --info vigilo-vigiconf

Pour finir, le plugin n'est compatible qu'avec GLPI >= 9.1.1,
qui doit être installé sur la même machine. La version de GLPI
peut être vérifiée depuis l'interface web de l'outil.


Installation
------------
L'installation se fait par la commande ``make install`` (à exécuter en
``root``).

Après installation, il est nécessaire d'enregistrer le fichier ``pkg/init``
en tant que ``/etc/rc.d/init.d/vigilo-glpi`` sur la machine qui héberge
GLPI, et de modifier la valeur de l'option ``httpd_svc`` dans le fichier
``/etc/vigilo/vigiconf/settings-local.ini`` de cette même machine::

    httpd_svc     = vigilo-glpi

De plus, il faut ajouter l'utilisateur ``vigiconf`` dans le groupe ``apache``::

    usermod -a -G apache vigiconf

Enfin, si cela n'a pas été fait lors de l'installation, il faut ajouter
des droits sudo à l'utilisateur ``apache`` pour lui permettre d'exécuter
VigiConf.
Pour cela, copier (en tant que ``root``) le fichier ``pkg/sudoers`` vers
``/etc/sudoers.d/vigilo-glpi``.


Utilisation
-----------
VigiConf s'exécute par la commande ``vigiconf``. Utilisez l'option ``--help``
pour découvrir les fonctionnalités disponibles.


License
-------
VigiConf est sous licence `GPL v2`_.


.. _documentation officielle: Vigilo_
.. _Vigilo: http://www.vigilo-nms.com
.. _GPL v2: http://www.gnu.org/licenses/gpl-2.0.html

.. vim: set syntax=rst fileencoding=utf-8 tw=78 :


