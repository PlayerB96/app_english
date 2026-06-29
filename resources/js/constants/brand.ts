export const BRAND_NAME = "DevEnglish";

export const BRAND_LOGO_ALT = BRAND_NAME;

/** Relación ancho/alto del logo compacto (595×132 px originales). */
export const BRAND_LOGO_ASPECT = 595 / 132;

export const BRAND_LOGO = {
    light: {
        src: "/images/brand/logo-compact.png",
        srcSet: "/images/brand/logo-compact@2x.png 2x",
    },
    dark: {
        src: "/images/brand/logo-compact-dark.png",
        srcSet: "/images/brand/logo-compact-dark@2x.png 2x",
    },
} as const;

export const BRAND_LOGO_HEIGHT = {
    sm: 22,
    md: 28,
    lg: 34,
} as const;

export type BrandLogoSize = keyof typeof BRAND_LOGO_HEIGHT;

export const BRAND_ICON = {
    light: {
        sm: "/images/brand/logo-icon-32.png",
        md: "/images/brand/logo-icon-64.png",
        lg: "/images/brand/logo-icon-180.png",
    },
    dark: {
        sm: "/images/brand/logo-icon-dark-32.png",
        md: "/images/brand/logo-icon-dark-64.png",
        lg: "/images/brand/logo-icon-dark-180.png",
    },
} as const;

export const BRAND_ICON_SIZE = {
    sm: 32,
    md: 64,
    lg: 180,
} as const;

export type BrandIconSize = keyof typeof BRAND_ICON_SIZE;
