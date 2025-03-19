package org.example.pdvteste;

import com.almasb.fxgl.net.Connection;

import java.sql.DriverManager;
import java.sql.SQLException;

public class Conexao {

    public static void main(String[] args) {

        try {
            Class.forName("com.mysql.jdbc.Driver");
            Connection conexao = DriverManager.getConnection("jdbc:mysql//localhost/banco", "usuario", "senha");

        } catch (ClassNotFoundException ex) {
            throw new RuntimeException("Não foi possível encontrar o driver do banco de dados: " + ex.getMessage());
        } catch (SQLException ex) {
            throw new RuntimeException("Ocorreu um erro ao acessar o banco: " + ex.getMessage());
        }

    }
}
