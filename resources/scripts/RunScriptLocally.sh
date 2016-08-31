bash -s << 'EOF'
    # Turn on quit on non-zero exit
    set -e
    {{ script }}
EOF
