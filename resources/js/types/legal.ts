export interface LegalBusiness {
    name: string;
    ruc: string;
    address: string;
    email: string;
    phone: string | null;
}

export interface LegalConfig {
    business: LegalBusiness;
    complaint_response_days: number;
}

export type ComplaintDocumentType = "dni" | "ce" | "pasaporte" | "ruc";
export type ComplaintItemType = "producto" | "servicio";
export type ComplaintType = "reclamo" | "queja";
