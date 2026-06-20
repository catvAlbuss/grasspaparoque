export type Evento = {
    id: number;
    nombre: string;
    precio: number;
    tipo_ambiente?: 'compartido' | 'propio';
    ambiente_grupo?: number | null;
};

export type BusyReservation = {
    id: number;
    id_evento: number;
    date: string;
    start_time: string;
    end_time: string;
};

export type ChatMessage = {
    role: 'bot' | 'user';
    text: string;
    time: string;
};

export type ChatOption = { label: string; value: string };

export type ReservationStep =
    | 'choose_event'
    | 'date'
    | 'time'
    | 'duration'
    | 'name'
    | 'lastname'
    | 'dni'
    | 'phone'
    | 'voucher_number'
    | 'voucher_file'
    | 'submitting'
    | 'done'
    | 'expired';

export type ReservationDraft = {
    id_evento: number | null;
    date: string;
    start_time: string;
    end_time: string;
    hours: number;
    name: string;
    lastname: string;
    dni: string;
    phone: string;
    payment_proof_number: string;
    payment_proof_file: File | null;
};
