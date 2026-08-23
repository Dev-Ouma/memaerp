/**
 * MEMA University – Shared Tailwind Configuration
 *
 * Brand Palette
 *   Primary   #0A3E50  – Dark Teal
 *   Secondary #1E8449  – Forest Green
 *   Accent    #E67E22  – Warm Orange
 *   White     #FFFFFF
 *
 * Typography
 *   Primary font : Quicksand  (--font-quicksand via next/font/google)
 *   Fallback font: Nunito     (--font-nunito   via next/font/google)
 *
 * @type {import('tailwindcss').Config}
 */
module.exports = {
  darkMode: ['class'],
  theme: {
    container: {
      center: true,
      padding: '2rem',
      screens: { '2xl': '1400px' },
    },
    extend: {
      // ── Typography ──────────────────────────────────────────────────────────
      fontFamily: {
        sans: [
          'var(--font-quicksand)',
          'var(--font-nunito)',
          'Quicksand',
          'Nunito',
          'system-ui',
          '-apple-system',
          'sans-serif',
        ],
        heading: [
          'var(--font-quicksand)',
          'var(--font-nunito)',
          'Quicksand',
          'Nunito',
          'system-ui',
          'sans-serif',
        ],
        body: [
          'var(--font-nunito)',
          'Nunito',
          'system-ui',
          'sans-serif',
        ],
      },

      // ── Brand Colour Palette ────────────────────────────────────────────────
      colors: {
        // Semantic tokens (Tailwind HSL-based — consumed by shadcn/ui components)
        border:      'hsl(var(--border))',
        input:       'hsl(var(--input))',
        ring:        'hsl(var(--ring))',
        background:  'hsl(var(--background))',
        foreground:  'hsl(var(--foreground))',
        primary: {
          DEFAULT:    'hsl(var(--primary))',
          foreground: 'hsl(var(--primary-foreground))',
        },
        secondary: {
          DEFAULT:    'hsl(var(--secondary))',
          foreground: 'hsl(var(--secondary-foreground))',
        },
        destructive: {
          DEFAULT:    'hsl(var(--destructive))',
          foreground: 'hsl(var(--destructive-foreground))',
        },
        muted: {
          DEFAULT:    'hsl(var(--muted))',
          foreground: 'hsl(var(--muted-foreground))',
        },
        accent: {
          DEFAULT:    'hsl(var(--accent))',
          foreground: 'hsl(var(--accent-foreground))',
        },
        popover: {
          DEFAULT:    'hsl(var(--popover))',
          foreground: 'hsl(var(--popover-foreground))',
        },
        card: {
          DEFAULT:    'hsl(var(--card))',
          foreground: 'hsl(var(--card-foreground))',
        },

        // MEMA-namespaced raw brand scales (use as mema-teal-800, mema-green-600, etc.)
        'mema-teal': {
          50:  '#EBF5F8',
          100: '#D3E9F0',
          200: '#AAD4E1',
          300: '#76B6CC',
          400: '#4494B3',
          500: '#227596',
          600: '#135A75',
          700: '#0E485E',
          800: '#0A3E50', // ← Primary Deep Teal  ← PRIMARY
          900: '#062936',
          950: '#03171F',
        },
        'mema-green': {
          50:  '#EAF7EE',
          100: '#D0EFD9',
          200: '#A6DFC0',
          300: '#6FC89E',
          400: '#3EAD7A',
          500: '#279462',
          600: '#1E8449', // ← Secondary Forest Green  ← SECONDARY
          700: '#186A3B',
          800: '#145530',
          900: '#114528',
          950: '#072614',
        },
        'mema-orange': {
          50:  '#FEF5EC',
          100: '#FDE8D3',
          200: '#FBD0A7',
          300: '#F8B275',
          400: '#F29445',
          500: '#E67E22', // ← Accent Warm Orange  ← ACCENT
          600: '#CB6514',
          700: '#9F4A0F',
          800: '#803B13',
          900: '#683313',
        },
        // Convenience aliases — same palette, shorter name
        'mema-primary':   '#0A3E50',
        'mema-secondary': '#1E8449',
        'mema-accent':    '#E67E22',
      },

      // ── Border Radius ───────────────────────────────────────────────────────
      borderRadius: {
        lg: 'var(--radius)',
        md: 'calc(var(--radius) - 2px)',
        sm: 'calc(var(--radius) - 4px)',
      },

      // ── Brand Shadows ───────────────────────────────────────────────────────
      boxShadow: {
        'card-subtle':   '0 1px 3px 0 rgba(10, 62, 80, 0.06), 0 1px 2px -1px rgba(10, 62, 80, 0.06)',
        'card-hover':    '0 10px 25px -5px rgba(10, 62, 80, 0.10), 0 8px 10px -6px rgba(10, 62, 80, 0.06)',
        'card-elevated': '0 20px 25px -5px rgba(10, 62, 80, 0.12), 0 8px 10px -6px rgba(10, 62, 80, 0.05)',
        'teal-glow':     '0 0 20px rgba(10, 62, 80, 0.25)',
        'green-glow':    '0 0 20px rgba(30, 132, 73, 0.25)',
        'orange-glow':   '0 0 20px rgba(230, 126, 34, 0.30)',
      },

      // ── Animations ──────────────────────────────────────────────────────────
      keyframes: {
        'accordion-down': {
          from: { height: '0' },
          to:   { height: 'var(--radix-accordion-content-height)' },
        },
        'accordion-up': {
          from: { height: 'var(--radix-accordion-content-height)' },
          to:   { height: '0' },
        },
        'pulse-glow': {
          '0%, 100%': { opacity: '1' },
          '50%':      { opacity: '0.6' },
        },
        'fade-in-up': {
          from: { opacity: '0', transform: 'translateY(16px)' },
          to:   { opacity: '1', transform: 'translateY(0)' },
        },
        'fade-in': {
          from: { opacity: '0' },
          to:   { opacity: '1' },
        },
        'slide-in-right': {
          from: { opacity: '0', transform: 'translateX(16px)' },
          to:   { opacity: '1', transform: 'translateX(0)' },
        },
      },
      animation: {
        'accordion-down':  'accordion-down 0.2s ease-out',
        'accordion-up':    'accordion-up 0.2s ease-out',
        'pulse-glow':      'pulse-glow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        'fade-in-up':      'fade-in-up 0.4s ease-out',
        'fade-in':         'fade-in 0.3s ease-out',
        'slide-in-right':  'slide-in-right 0.4s ease-out',
      },
    },
  },
  plugins: [],
};
