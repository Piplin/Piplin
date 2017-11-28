# Prepare

echo -e "Prepare {{ build_path }}"

cd {{ project_path }}

# Create the builds directory if it doesn't exist
if [ ! -d {{ builds_path }} ]; then
    mkdir {{ builds_path }}
fi

mkdir {{ build_path }}
cd {{ build_path }}

# Extract the archive
echo -e "Extracting...\n"
tar -m --gunzip --verbose --extract --file={{ remote_archive }} --directory={{ build_path }}

rm -f {{ remote_archive }}
