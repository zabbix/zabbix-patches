#!/bin/bash

# shows patch selection, applies the selected patches
# no error checking at all
# no conflict/dependency concept

command -v dialog > /dev/null 2>&1 || { echo >&2 "This script requires 'dialog'. Please install it!"; exit 1; }

[[ "$@" ]] || {
    echo "Usage:
$0 zabbix_version <target_directory>
default target_directory is /usr/share/zabbix

Example:
$0 3.2 /path/to/frontend"
    exit
}

zabbix_major_version=$1
if [ -z "$2" ]; then
    target_dir="/usr/share/zabbix"
else
    target_dir=$2
fi

[[ -d ${target_dir} ]] || {
    echo "target directory \"$target_dir\" does not exist"
    exit 1
}

[[ ${target_dir} =~ /$ ]] || target_dir=${target_dir}/

while IFS='|' read patch_id type details; do

    [[ ${type} = name ]] && {
        patch_name["$patch_id"]=${details}
        continue
    }
    [[ ${type} = f ]] && {
        patch_frontend_only["$patch_id"]=${details}
        continue
    }
    [[ ${type} = desc ]] && {
        patch_desc["$patch_id"]=${details}
        continue
    }
    # patch directory levels to remove
    [[ ${type} = ltr ]] && {
        patch_ltr["$patch_id"]=${details}
        continue
    }
    [[ ${type} = extra ]] && {
        patch_extra["$patch_id"]="${patch_extra[$patch_id]#$'\n'}"$'\n'"$details"
        continue
    }
done < <(tail -n +2 zabbix-${zabbix_major_version}/patches.def)

for ((patchid=1; patchid<${#patch_name[@]}+1; patchid++)); do
    patchlist+=(${patchid} "${patch_name[$patchid]#zabbix-$zabbix_major_version-} ${patch_desc[$patchid]}" off)
done

patches=$(dialog --stdout --checklist "Choose the patches to apply" 0 0 0 "${patchlist[@]}")

[[ ${patches} ]] || {
    echo
    echo "No patches selected"
    exit
}

pushd zabbix-${zabbix_major_version} > /dev/null
for patch in ${patches}; do
    echo "Applying ${patch_name[$patch]}"
    if [ -d ${target_dir}${patch_frontend_only["$patch"]:+frontends/php/} ]; then
        working_directory=${target_dir}${patch_frontend_only["$patch"]:+frontends/php/}
    else
        working_directory=${target_dir}
    fi
    cp ${patch_name[$patch]}/${patch_name[$patch]}.patch ${working_directory}
    pushd ${working_directory} > /dev/null
    patch -p ${patch_ltr["$patch"]:-0} -i ${patch_name[$patch]}.patch
    rm ${patch_name[$patch]}.patch
    popd > /dev/null
    while read extra; do
        echo "copying file: $extra"
        cp ${extra}
    done < <(echo "${patch_extra[$patch]}" | sed -e "s| | $working_directory|" -e "s|^|${patch_name[$patch]}/|") 2> /dev/null
done
popd > /dev/null
