/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: ["class"],
  theme: {
    container: {
      center: true,
      padding: "2rem",
      screens: {
        "2xl": "1400px",
      },
    },
    extend: {
      colors: {
        border: "hsl(var(--border))",
        input: "hsl(var(--input))",
        ring: "hsl(var(--ring))",
        background: "hsl(var(--background))",
        foreground: "hsl(var(--foreground))",
        mema: {
          teal: {
            50: "#EBF5F8",
            100: "#D3E9F0",
            200: "#AAD4E1",
            300: "#76B6CC",
            400: "#4494B3",
            500: "#227596",
            600: "#135A75",
            700: "#0E485E",
            800: "#0A3E50", // Primary Deep Teal
            900: "#062936",
            950: "#03171F",
          },
          green: {
            50: "#EAF7EE",
            100: "#D0EFD9",
            200: "#A6DFC0",
            300: "#6FC89E",
            400: "#3EAD7A",
            500: "#279462",
            600: "#1E8449", // Secondary Forest Green
            700: "#186A3B",
            800: "#145530",
            900: "#114528",
            950: "#072614",
          },
          gold: {
            50: "#FEF9EB",
            100: "#FDF1CC",
            200: "#FBE199",
            300: "#F8CD5F",
            400: "#F5BA2F",
            500: "#E09C0D",
            600: "#B7791F", // Warning Gold
            700: "#925A12",
            800: "#764713",
            900: "#633B13",
          },
        },
        primary: {
          DEFAULT: "hsl(var(--primary))",
          foreground: "hsl(var(--primary-foreground))",
        },
        secondary: {
          DEFAULT: "hsl(var(--secondary))",
          foreground: "hsl(var(--secondary-foreground))",
        },
        destructive: {
          DEFAULT: "hsl(var(--destructive))",
          foreground: "hsl(var(--destructive-foreground))",
        },
        muted: {
          DEFAULT: "hsl(var(--muted))",
          foreground: "hsl(var(--muted-foreground))",
        },
        accent: {
          DEFAULT: "hsl(var(--accent))",
          foreground: "hsl(var(--accent-foreground))",
        },
        popover: {
          DEFAULT: "hsl(var(--popover))",
          foreground: "hsl(var(--popover-foreground))",
        },
        card: {
          DEFAULT: "hsl(var(--card))",
          foreground: "hsl(var(--card-foreground))",
        },
      },
      borderRadius: {
        lg: "var(--radius)",
        md: "calc(var(--radius) - 2px)",
        sm: "calc(var(--radius) - 4px)",
      },
      fontFamily: {
        sans: ["var(--font-inter)", "system-ui", "-apple-system", "sans-serif"],
        heading: ["var(--font-outfit)", "system-ui", "sans-serif"],
      },
      boxShadow: {
        'card-subtle': '0 1px 3px 0 rgba(10, 62, 80, 0.05), 0 1px 2px -1px rgba(10, 62, 80, 0.05)',
        'card-hover': '0 10px 25px -5px rgba(10, 62, 80, 0.08), 0 8px 10px -6px rgba(10, 62, 80, 0.05)',
        'card-elevated': '0 20px 25px -5px rgba(10, 62, 80, 0.1), 0 8px 10px -6px rgba(10, 62, 80, 0.04)',
      },
      keyframes: {
        "accordion-down": {
          from: { height: "0" },
          to: { height: "var(--radix-accordion-content-height)" },
        },
        "accordion-up": {
          from: { height: "var(--radix-accordion-content-height)" },
          to: { height: "0" },
        },
        "pulse-glow": {
          "0%, 100%": { opacity: "1" },
          "50%": { opacity: "0.6" },
        },
      },
      animation: {
        "accordion-down": "accordion-down 0.2s ease-out",
        "accordion-up": "accordion-up 0.2s ease-out",
        "pulse-glow": "pulse-glow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite",
      },
    },
  },
  plugins: [],
};
