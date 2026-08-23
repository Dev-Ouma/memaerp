/** @type {import('next').NextConfig} */
const nextConfig = {
  transpilePackages: ['@mema/ui', '@mema/api-client', '@mema/types'],
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
