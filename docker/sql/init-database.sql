IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = N'ln1_caja_rapida')
BEGIN
    CREATE DATABASE [ln1_caja_rapida];
END
GO
