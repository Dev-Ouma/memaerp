/** @type {import('next').NextConfig} */
const nextConfig = {
  transpilePackages: ['@mema/ui', '@mema/auth', '@mema/api-client', '@mema/types'],
};

module.exports = nextConfig;
