#!/bin/bash

# 最初に依存をインストール
cd /app

if [ ! -d node_modules ]; then
  echo "Installing npm dependencies..."
  npm install
fi

echo "Running Vite dev server..."
npm run dev -- --host
