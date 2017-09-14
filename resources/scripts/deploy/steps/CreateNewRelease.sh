# Create new release

echo -e " Create new release"

cd {{ project_path }}

# Create the releases directory if it doesn't exist
if [ ! -d {{ releases_path }} ]; then
    mkdir {{ releases_path }}
fi

# Create the shared directory if it doesn't exist
if [ ! -d {{ shared_path }} ]; then
    mkdir {{ shared_path }}
fi

mkdir {{ release_path }}
cd {{ release_path }}

# Extract the archive
echo -e "Extracting...\n"
tar --warning=no-timestamp --gunzip --verbose --extract --file={{ remote_archive }} --directory={{ release_path }}

rm -f {{ remote_archive }}
