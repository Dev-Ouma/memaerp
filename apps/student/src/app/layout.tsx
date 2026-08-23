import './globals.css';
import { AppProviders } from '@mema/auth';
import { Quicksand, Nunito } from 'next/font/google';

const quicksand = Quicksand({
  subsets: ['latin'],
  variable: '--font-quicksand',
  display: 'swap',
});

const nunito = Nunito({
  subsets: ['latin'],
  variable: '--font-nunito',
  display: 'swap',
});

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" className={`${quicksand.variable} ${nunito.variable}`}>
      <head>
        <title>MEMA Student Portal — University Information System</title>
        <meta
          name="description"
          content="Official Mema University Student Information & Lifecycle Portal"
        />
      </head>
      <body className="font-sans antialiased">
        <AppProviders>{children}</AppProviders>
      </body>
    </html>
  );
}
