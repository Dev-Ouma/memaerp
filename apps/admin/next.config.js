/** @type {import('next').NextConfig} */
const nextConfig = {
  transpilePackages: ['@mema/ui', '@mema/auth', '@mema/api-client', '@mema/types', '@mema/tables'],
  images: {
    remotePatterns: [
      {
        protocol: 'https',
        hostname: 'images.unsplash.com',
      },
    ],
  },
};

module.exports = nextConfig;
