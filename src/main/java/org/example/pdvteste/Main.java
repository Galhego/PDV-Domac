package org.example.pdvteste;

import javafx.application.Application;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.scene.image.Image;
import javafx.scene.layout.Region;
import javafx.scene.paint.Color;
import javafx.stage.Stage;
import javafx.stage.StageStyle;
import javafx.scene.shape.Rectangle;

public class Main extends Application {

    @Override
    public void start(Stage stage) throws Exception {
        // Carrega o arquivo FXML
        FXMLLoader fxmlLoader = new FXMLLoader(Main.class.getResource("hello-view.fxml"));
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

    public static void main(String[] args) {
        launch();
    }
}
