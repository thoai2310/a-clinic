FROM node:22-alpine

WORKDIR /app

# Install global tools
RUN npm install -g @vue/cli @vitejs/plugin-vue

# Create entrypoint script
RUN cat > /entrypoint.sh << 'EOF'
#!/bin/sh

echo "🔍 Checking working directory: $(pwd)"
echo "📂 Listing files: $(ls -la)"

# Check if package.json exists
if [ -f "package.json" ]; then
    echo "📦 Found package.json"

    # Check if node_modules exists and has content
    if [ ! -d "node_modules" ] || [ -z "$(ls -A node_modules 2>/dev/null)" ]; then
        echo "🚀 Installing dependencies... (this may take a few minutes)"
        npm install
        echo "✅ Dependencies installed successfully!"
    else
        echo "✅ Dependencies already installed"
    fi

    echo "🚀 Starting development server..."
    exec npm run dev -- --host 0.0.0.0 --port 5173
else
    echo "❌ No package.json found in $(pwd)"
    echo "⏳ Waiting..."
    tail -f /dev/null
fi
EOF

RUN chmod +x /entrypoint.sh

EXPOSE 5173

ENTRYPOINT ["/entrypoint.sh"]