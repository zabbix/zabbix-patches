zabbix-patches
==============

Community maintained patches for Zabbix

## Usage

```
git clone https://github.com/zabbix/zabbix-patches.git
cd zabbix-patches
./patch-zabbix.sh 4.0 ~/Desktop/zabbix-4.0.0
```

## Available patches

Please, vote for ZBXNEXT you're using or just care about - that could help to solve them \ move these patches to upstream.

### Zabbix 2.4

[ZBXNEXT-543](https://support.zabbix.com/browse/ZBXNEXT-543): Adds an option to clear items and triggers while using hosts and templates window on template page.

[ZBXNEXT-1061](https://support.zabbix.com/browse/ZBXNEXT-1061): Adds pin/unpin icon that allows keeping the time period when switching between graphs and screens

[ZBXNEXT-1109](https://support.zabbix.com/browse/ZBXNEXT-1109): Adds ability for zabbix-sender send values to two servers at same time

[ZBXNEXT-1603](https://support.zabbix.com/browse/ZBXNEXT-1603): Adds support of DB READONLY flag in configuration of frontend

[ZBXNEXT-2347](https://support.zabbix.com/browse/ZBXNEXT-2347): Add possibility to export inventory page data to CSV

[ZBXNEXT-2819](https://support.zabbix.com/browse/ZBXNEXT-2819): Adds an option to disable showing groups without problems in host and system status (fixed since **3.4**)

### Zabbix 3.0

[ZBXNEXT-4640](https://support.zabbix.com/browse/ZBXNEXT-4640): [Keycloak](https://www.keycloak.org/) authentication integration

[ZBXNEXT-1109](https://support.zabbix.com/browse/ZBXNEXT-1109): Adds ability for zabbix-sender send values to two servers at same time

[ZBXNEXT-1456](https://support.zabbix.com/browse/ZBXNEXT-1456): Filter discovered items on items list page (fixed since **4.0**)

[ZBXNEXT-1603](https://support.zabbix.com/browse/ZBXNEXT-1603): Adds support of DB READONLY flag in configuration of frontend

[ZBXNEXT-1810](https://support.zabbix.com/browse/ZBXNEXT-1810): Filter latest data by item value (regex and substring)

[ZBXNEXT-2315](https://support.zabbix.com/browse/ZBXNEXT-2315): Include response headers for httptest regex required strings

[ZBXNEXT-2347](https://support.zabbix.com/browse/ZBXNEXT-2347): Add possibility to export inventory page data to CSV

[ZBXNEXT-2448](https://support.zabbix.com/browse/ZBXNEXT-2448): Do not truncate ITEM.VALUE and ITEM.LASTVALUE after 20 characters in the frontend

[ZBXNEXT-2819](https://support.zabbix.com/browse/ZBXNEXT-2819): Adds an option to disable showing groups without problems in system status (fixed since **3.4**)

[ZBX-5470](https://support.zabbix.com/browse/ZBX-5470): Add template_id in "Template cannot be linked to another template" exception

### Zabbix 3.2

[ZBXNEXT-543](https://support.zabbix.com/browse/ZBXNEXT-543): Adds an option to clear items and triggers while using hosts and templates window on template page.

[ZBXNEXT-1109](https://support.zabbix.com/browse/ZBXNEXT-1109): Adds ability for zabbix-sender send values to two servers at same time

[ZBXNEXT-1456](https://support.zabbix.com/browse/ZBXNEXT-1456): Filter discovered items on items list page (fixed since **4.0**)

[ZBXNEXT-2315](https://support.zabbix.com/browse/ZBXNEXT-2315): Include response headers for httptest regex required strings

[ZBXNEXT-2819](https://support.zabbix.com/browse/ZBXNEXT-2819): Adds an option to disable showing groups without problems in system status (fixed since **3.4**)

[ZBXNEXT-3089](https://support.zabbix.com/browse/ZBXNEXT-3089): Adds support of PK(itemid,clock) for history, history_uint tables

[ZBXNEXT-1510](https://support.zabbix.com/browse/ZBXNEXT-1510): Add information about creator to maintenance description, default 3h maintenance period, merge all maintenance settings to one tab

[ZBX-5470](https://support.zabbix.com/browse/ZBX-5470): Add template_id in "Template cannot be linked to another template" exception

[ZBX-12251](https://support.zabbix.com/browse/ZBX-12251): Fix cached trigger state not being recalculated in case of problem during original state change (fixed since **4.0**)

[ZBX-12423](https://support.zabbix.com/browse/ZBX-12423): Improve WEB UI - Show Server Name instead of server in the right corner of UI.

### Zabbix **3.4**

[ZBXNEXT-543](https://support.zabbix.com/browse/ZBXNEXT-543): Adds an option to clear items and triggers while using hosts and templates window on template page.

[ZBXNEXT-1109](https://support.zabbix.com/browse/ZBXNEXT-1109): Adds ability for zabbix-sender send values to two servers at same time

[ZBXNEXT-1456](https://support.zabbix.com/browse/ZBXNEXT-1456): Filter discovered items on items list page (fixed since **4.0**)

[ZBXNEXT-2315](https://support.zabbix.com/browse/ZBXNEXT-2315): Include response headers for httptest regex required strings

[ZBXNEXT-3089](https://support.zabbix.com/browse/ZBXNEXT-3089): Adds support of PK(itemid,clock) for history, history_uint tables.

[ZBXNEXT-1510](https://support.zabbix.com/browse/ZBXNEXT-1510): Add information about creator to maintenance description, default 3h maintenance period, merge all maintenance settings to one tab

[ZBX-5470](https://support.zabbix.com/browse/ZBX-5470): Add template_id in "Template cannot be linked to another template" exception

[ZBX-12423](https://support.zabbix.com/browse/ZBX-12423): Improve WEB UI - Show Server Name instead of server in the right corner of UI.

### Zabbix **4.0**

[ZBXNEXT-1109](https://support.zabbix.com/browse/ZBXNEXT-1109): Adds ability for zabbix-sender send values to two servers at same time

[ZBXNEXT-2315](https://support.zabbix.com/browse/ZBXNEXT-2315): Include response headers for httptest regex required strings

[ZBXNEXT-3089](https://support.zabbix.com/browse/ZBXNEXT-3089): Adds support of PK(itemid,clock) for history, history_uint tables.

[ZBXNEXT-1510](https://support.zabbix.com/browse/ZBXNEXT-1510): Add information about creator to maintenance description, default 3h maintenance period, merge all maintenance settings to one tab

[ZBX-5470](https://support.zabbix.com/browse/ZBX-5470): Add template_id in "Template cannot be linked to another template" exception

[ZBX-12423](https://support.zabbix.com/browse/ZBX-12423): Improve WEB UI - Show Server Name instead of server in the right corner of UI.
