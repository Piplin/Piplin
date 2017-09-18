# Create release archive

echo -e "Create release archive"

cd {{ mirror_path }}

(git archive --format=tar {{ sha }} | gzip > {{ release_archive }})
