import { QRCodeSVG } from "qrcode.react";
import Barcode from "react-barcode";
import { Modal } from "../ui/modal";
import Button from "../ui/button/Button";

interface Props {
  isOpen: boolean;
  onClose: () => void;
  codigo: string;
  nombre: string;
}

export default function LabelModal({ isOpen, onClose, codigo, nombre }: Props) {
  return (
    <>
      <style>{`
        @media print {
          body * { visibility: hidden; }
          #label-content, #label-content * { visibility: visible; }
          #label-content { position: absolute; left: 0; top: 0; padding: 20px; }
        }
      `}</style>
      <Modal isOpen={isOpen} onClose={onClose} className="max-w-sm p-6 m-4">
        <div id="label-content" className="flex flex-col items-center gap-4 py-4">
          <h2 className="text-lg font-semibold text-gray-800 dark:text-white/90">
            {nombre}
          </h2>
          <QRCodeSVG value={codigo} size={160} level="M" />
          <Barcode value={codigo} width={1.5} height={50} fontSize={14} margin={0} />
          <span className="text-sm text-gray-500 dark:text-gray-400">{codigo}</span>
        </div>
        <div className="flex justify-center gap-3 pt-2">
          <Button size="sm" variant="outline" onClick={onClose}>Cerrar</Button>
          <Button size="sm" onClick={() => window.print()}>Imprimir</Button>
        </div>
      </Modal>
    </>
  );
}
