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

export const metadata = {
  title: 'MEMA ERP — Enterprise Administration Console',
  description: 'Mema University Enterprise Resource Planning & Administration',
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" className={`${quicksand.variable} ${nunito.variable}`}>
      <body
        style={{
          fontFamily: "var(--font-quicksand,'Quicksand'),var(--font-nunito,'Nunito'),system-ui,-apple-system,sans-serif",
          margin: 0,
          padding: 0,
          WebkitFontSmoothing: 'antialiased' as const,
        }}
      >
        <AppProviders>{children}</AppProviders>
      </body>
    </html>
  );
}
