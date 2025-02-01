import React, { useState } from 'react';

const RafflePage = () => {
  const [tickets, setTickets] = useState(Array(50).fill(''));
  const [selectedNumber, setSelectedNumber] = useState('');
  const [name, setName] = useState('');

  const handleTicketSelect = (index) => {
    if (tickets[index] === '') {
      const updatedTickets = [...tickets];
      updatedTickets[index] = name;
      setTickets(updatedTickets);
      setName('');
      setSelectedNumber('');
    }
  };

  return (
    <div className="max-w-4xl mx-auto p-6">
      <div className="text-center mb-8">
        <h1 className="text-4xl font-bold text-purple-600 mb-4">Gran Rifa</h1>
        <div className="bg-purple-100 p-4 rounded-lg mb-4">
          <h2 className="text-xl mb-2">Perfume para Dama</h2>
          <p className="text-lg font-semibold text-purple-700">MITHYKA L'BEL 50ml</p>
          <p className="mt-2">Fecha: 15 de Julio del 2023</p>
          <p className="text-2xl font-bold text-purple-600 mt-2">$1</p>
        </div>
      </div>

      <div className="mb-6">
        <input
          type="text"
          value={name}
          onChange={(e) => setName(e.target.value)}
          placeholder="Ingrese su nombre"
          className="w-full p-2 border border-purple-300 rounded"
        />
      </div>

      <div className="grid grid-cols-5 gap-2">
        {tickets.map((ticket, index) => (
          <button
            key={index}
            onClick={() => handleTicketSelect(index)}
            className={`p-4 text-center border rounded-lg ${
              ticket ? 'bg-purple-200 cursor-not-allowed' : 'bg-white hover:bg-purple-50 cursor-pointer'
            }`}
            disabled={ticket !== ''}
          >
            <div className="font-bold">{(index + 1).toString().padStart(2, '0')}</div>
            <div className="text-sm truncate">{ticket}</div>
          </button>
        ))}
      </div>

      <div className="mt-6 p-4 bg-purple-50 rounded-lg">
        <h3 className="font-bold mb-2">Instrucciones:</h3>
        <ol className="list-decimal list-inside space-y-2">
          <li>Ingrese su nombre en el campo de texto</li>
          <li>Seleccione un número disponible</li>
          <li>Los números seleccionados mostrarán el nombre del participante</li>
          <li>Precio por número: $1</li>
        </ol>
      </div>
    </div>
  );
};

export default RafflePage;