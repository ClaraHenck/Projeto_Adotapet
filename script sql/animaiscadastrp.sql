use adotapet;
SELECT * FROM animais;
INSERT INTO animais
(`id`,
`ong_id`,
`nome`,
`especie_raca`,
`idade_estimada`,
`porte`,
`carteira_vacinacao`,
`foto_url`,
`descricao`,
`data_cadastro`)
VALUES
(12345678,
2,
'belinha',
'Poddle',
15,
'grande',
'completa',
'https://images.unsplash.com/photo-1543466835-00a7907e9de1?q=80&w=500',
'descricao',
'2026-07-29 14:30:00');
SELECT * FROM adotapet_db.adotantes;