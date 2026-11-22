# Data Dummy (multi sekolah)

Jalankan `database/dummy_data.sql` SETELAH schema/migrasi. Hapus bersih dengan `database/hapus_data_dummy.sql` (hanya menyentuh sekolah ID 901 & 902).

| Peran | Username | Password |
|---|---|---|
| Super Admin | `super.dummy` | `Super12345` |
| Admin SMP Negeri 1 Contoh | `admin.smp1` | `Admin12345` |
| Guru SMP (3) | `smp1.guru1` .. `smp1.guru3` | `Guru12345` |
| Orang tua SMP | `smp1.ortu1`, `smp1.ortu2` | `Ortu12345` |
| Admin SMA Harapan Bangsa | `admin.sma2` | `Admin12345` |
| Guru SMA (3) | `sma2.guru1` .. `sma2.guru3` | `Guru12345` |
| Orang tua SMA | `sma2.ortu1`, `sma2.ortu2` | `Ortu12345` |

Tiap sekolah: 2 kelas, 12 siswa (QR aktif), 3 mapel, jadwal Senin-Jumat, absensi 16-20 Nov 2026, pengumuman, kalender, SPP (4 lunas), ekskul, dan 1 izin menunggu.
SMA sengaja berkuota 12 (penuh) untuk menguji pembatasan kuota. Nomor WhatsApp orang tua fiktif.
