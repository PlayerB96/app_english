export interface PowerShopPackage {
    power: number;
    soles: number;
}

export interface PowerShopPaymentMethod {
    phone: string;
    holder: string;
    qr_image: string;
}

export interface PowerShopConfig {
    packages: PowerShopPackage[];
    yape: PowerShopPaymentMethod;
    plin: PowerShopPaymentMethod;
}
