package org.example.pdvteste;

import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.scene.control.Alert.AlertType;
import javafx.scene.control.Button;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;
import javafx.scene.image.Image;
import javafx.scene.layout.Region;
import javafx.scene.paint.Color;
import javafx.scene.shape.Rectangle;
import javafx.stage.Stage;
import javafx.stage.StageStyle;

import java.io.IOException;
import java.io.InputStream;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.util.Properties;

public class LoginController {

    @FXML
    private Button button;
    @FXML
    private TextField username;
    @FXML
    private PasswordField password;
    @FXML
    private Button fecharBtn;


    @FXML
    public void fecharAction(ActionEvent e) {
        Stage stage = (Stage) fecharBtn.getScene().getWindow();
        stage.close();
    }

    @FXML
    public void joinVendas() throws IOException {
        Stage stage = new Stage();

        // Carrega o arquivo FXML
        FXMLLoader fxmlLoader = new FXMLLoader(Main.class.getResource("/Vendas.fxml"));
        stage.initStyle(StageStyle.TRANSPARENT); // Torna a janela transparente

        // Adicionando icone à hotbar
        Image icon = new Image(getClass().getResourceAsStream("/org/example/pdvteste/images/logo_seu_coxinha.png"));
        stage.getIcons().add(icon);

        // Cria a cena com fundo transparente
        Scene scene = new Scene(fxmlLoader.load(), 1100, 700);
        scene.setFill(Color.TRANSPARENT); // Define o fundo como transparente

        // Define um retângulo com cantos arredondados como máscara (clip)
        Rectangle clip = new Rectangle();
        clip.setWidth(scene.getWidth());
        clip.setHeight(scene.getHeight());
        clip.setArcWidth(30); // Raio horizontal dos cantos arredondados
        clip.setArcHeight(30); // Raio vertical dos cantos arredondados

        // Aplica o clip ao root da cena
        Region root = (Region) scene.getRoot();
        root.setClip(clip);

        // Adiciona a cena à janela
        stage.setScene(scene);
        stage.show();

        // Permite mover a janela clicando e arrastando
        scene.setOnMousePressed(event -> {
            xOffset = event.getSceneX();
            yOffset = event.getSceneY();
        });

        scene.setOnMouseDragged(event -> {
            stage.setX(event.getScreenX() - xOffset);
            stage.setY(event.getScreenY() - yOffset);
        });
    }

    private double xOffset = 0;
    private double yOffset = 0;

    @FXML
    private void userLogin(ActionEvent event) throws Exception {
        // Lógica de validação
        String user = username.getText();
        String pass = password.getText();

        // Validação dos campos
        if (user== null || user.trim().isEmpty() || pass == null || pass.trim().isEmpty()) {
            Alert alert = new Alert(AlertType.ERROR);
            alert.setTitle("Erro de Login");
            alert.setHeaderText(null);
            alert.setContentText("Erro na validação dos campos!");
            alert.showAndWait();
        }

        // Normaliza o e-mail
        user = user.trim().toLowerCase();

        Connection conn = null;
        PreparedStatement ps = null;
        ResultSet rs = null;

        Properties props = new Properties();
        // Carrega o arquivo db.properties dos recursos do projeto
        InputStream input = LoginController.class.getClassLoader().getResourceAsStream("db.properties");
        if (input == null) {
            throw new Exception("Arquivo db.properties não encontrado.");
        }
        props.load(input);

        String url = props.getProperty("db.url");
        String username = props.getProperty("db.username");
        String password = props.getProperty("db.password");
        String driver = props.getProperty("db.driver");

        // Conecta ao banco
        Class.forName(driver);
        conn = DriverManager.getConnection(url, username, password);

        /*String sql_create_table = "CREATE TABLE IF NOT EXISTS funcionarios ("
                + "cd_funcionario INT AUTO_INCREMENT PRIMARY KEY, "
                + "nm_funcionario VARCHAR(255) NOT NULL, "
                + "cd_senha_funcionario VARCHAR(255) NOT NULL, "
                + "is_Adm BOOLEAN NOT NULL DEFAULT FALSE"
                + ");";

        stmt.executeUpdate(sql_create_table);*/

        // Consulta SQL para buscar o usuário
        String sql = "SELECT nm_funcionario, cd_funcionario, cd_senha_funcionario FROM funcionarios WHERE nm_funcionario = ?";
        ps = conn.prepareStatement(sql);
        ps.setString(1, user);
        rs = ps.executeQuery();

        if (rs.next()) {
            String senhaBanco = rs.getString("cd_senha_funcionario");

            // Valida a senha diretamente (sem hash)
            if (pass.equals(senhaBanco) ) {
                /* Popup de sucesso
                Alert alert = new Alert(AlertType.INFORMATION);
                alert.setTitle("Login");
                alert.setHeaderText(null);
                alert.setContentText("Login bem-sucedido!");
                alert.showAndWait();
                */
                Stage stage = (Stage) button.getScene().getWindow();
                stage.close();
                joinVendas();
            } else {
                // Popup de erro
                Alert alert = new Alert(AlertType.ERROR);
                alert.setTitle("Erro de Login");
                alert.setHeaderText(null);
                alert.setContentText("Usuário ou senha incorretos!");
                alert.showAndWait();
            }

        }else {
            // Popup de erro: usuário não encontrado
            Alert alert = new Alert(AlertType.ERROR);
            alert.setTitle("Erro de Login");
            alert.setHeaderText(null);
            alert.setContentText("Usuário ou senha incorretos!");
            alert.showAndWait();
        }
    }
}
