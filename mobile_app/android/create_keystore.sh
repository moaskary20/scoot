#!/bin/bash

# Script to create keystore for Android app signing
# This script will guide you through creating a keystore file

echo "============================================"
echo "LinerScoot - Keystore Creation Script"
echo "============================================"
echo ""

# Get the script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
ANDROID_DIR="$SCRIPT_DIR"
PROJECT_DIR="$(dirname "$ANDROID_DIR")"

# Default keystore location
KEYSTORE_NAME="upload-keystore.jks"
KEYSTORE_PATH="$HOME/$KEYSTORE_NAME"

echo "This script will help you create a keystore for signing your Android app."
echo ""
echo "IMPORTANT: Keep the keystore file and password in a safe place!"
echo "If you lose the keystore, you won't be able to update your app on Google Play."
echo ""
read -p "Press Enter to continue or Ctrl+C to cancel..."

echo ""
echo "Step 1: Creating keystore file..."
echo "Keystore will be saved to: $KEYSTORE_PATH"
echo ""

# Check if keystore already exists
if [ -f "$KEYSTORE_PATH" ]; then
    echo "WARNING: Keystore file already exists at: $KEYSTORE_PATH"
    read -p "Do you want to overwrite it? (yes/no): " overwrite
    if [ "$overwrite" != "yes" ]; then
        echo "Cancelled. Using existing keystore."
    else
        rm "$KEYSTORE_PATH"
        echo "Creating new keystore..."
    fi
fi

# Create keystore
keytool -genkey -v -keystore "$KEYSTORE_PATH" -keyalg RSA -keysize 2048 -validity 10000 -alias upload

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Keystore created successfully!"
    echo ""
    
    # Step 2: Create key.properties file
    echo "Step 2: Creating key.properties file..."
    echo ""
    
    # Get passwords
    echo "Enter the keystore password you just created:"
    read -s STORE_PASSWORD
    echo ""
    echo "Re-enter the password to confirm:"
    read -s STORE_PASSWORD_CONFIRM
    echo ""
    
    if [ "$STORE_PASSWORD" != "$STORE_PASSWORD_CONFIRM" ]; then
        echo "❌ Passwords don't match! Please run the script again."
        exit 1
    fi
    
    KEY_PROPERTIES_FILE="$ANDROID_DIR/key.properties"
    
    # Check if key.properties already exists
    if [ -f "$KEY_PROPERTIES_FILE" ]; then
        echo "WARNING: key.properties file already exists!"
        read -p "Do you want to overwrite it? (yes/no): " overwrite_props
        if [ "$overwrite_props" != "yes" ]; then
            echo "Keeping existing key.properties file."
            exit 0
        fi
    fi
    
    # Create key.properties
    cat > "$KEY_PROPERTIES_FILE" << EOF
storePassword=$STORE_PASSWORD
keyPassword=$STORE_PASSWORD
keyAlias=upload
storeFile=$KEYSTORE_PATH
EOF
    
    # Set proper permissions
    chmod 600 "$KEY_PROPERTIES_FILE"
    
    echo "✅ key.properties file created successfully!"
    echo ""
    echo "============================================"
    echo "✅ Setup Complete!"
    echo "============================================"
    echo ""
    echo "Files created:"
    echo "  - Keystore: $KEYSTORE_PATH"
    echo "  - key.properties: $KEY_PROPERTIES_FILE"
    echo ""
    echo "Next steps:"
    echo "  1. Keep these files in a safe place!"
    echo "  2. Build your app: cd $PROJECT_DIR && flutter build appbundle --release"
    echo ""
    echo "⚠️  IMPORTANT:"
    echo "  - Never commit keystore or key.properties to git!"
    echo "  - Keep a backup of the keystore file"
    echo "  - Remember your password!"
    echo ""
    
else
    echo ""
    echo "❌ Failed to create keystore. Please check the error above."
    exit 1
fi

